using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;
public class musicbaru : MonoBehaviour {
	public Slider volume;
	public AudioSource mymusic;
	public GameObject done;
	public Button button;
	public GameObject musik;
	public GameObject mati;
	public int counter =0;

	public AudioSource audio;
	// Use this for initialization
	void Start () {
		button = GetComponent<Button> ();
		audio = GetComponent<AudioSource> ();
	}
	public void toggle(){
		if (AudioListener.volume == 0.0f) {
			AudioListener.volume = 1.0f;
			musik.SetActive (true);
			mati.SetActive (false);
		
		} else if (AudioListener.volume == 1.0f) {
			AudioListener.volume = 0.0f;
			musik.SetActive (false);
			mati.SetActive (true);

		}
	}
	// Update is called once per frame
	void Update () {
		mymusic.volume = volume.value;
	}
	public void doneepanel(){
		done.SetActive (false);
	}
	public void showpanelmusic(){
		done.SetActive (true);
	}
}
