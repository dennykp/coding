using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;
public class setuang : MonoBehaviour {
	public GameObject hideuang;
	public InputField name;
	public GameObject hideangka;
	public Text fText;
	// Use this for initialization
	public GameObject[] angka;
	private AudioSource angkasound;
	public AudioClip soundangka;
	private AudioSource mulaisound;
	public AudioClip soundmulai;
	void Start () {
		
	}
	void Awake(){
		angkasound = GetComponent<AudioSource> ();
		mulaisound = GetComponent<AudioSource> ();
	}
	IEnumerator delayangka(){
		yield return new WaitForSeconds (1);
		hideangka.SetActive (true);
		yield return new WaitForSeconds (0.5f);
		angkasound.PlayOneShot (soundangka);
		angka [2].SetActive (true);
		yield return new WaitForSeconds (0.75f);
	
		angka [2].SetActive (false);
		yield return new WaitForSeconds (0.90f);
		angkasound.PlayOneShot (soundangka);
		angka [1].SetActive (true);
		yield return new WaitForSeconds (0.75f);
		angka [1].SetActive (false);
		yield return new WaitForSeconds (0.98f);
		angkasound.PlayOneShot (soundangka);
		angka [0].SetActive (true);
		yield return new WaitForSeconds (0.50f);
		angka [0].SetActive (false);
		yield return new WaitForSeconds (0.90f);

		hideangka.SetActive (false);
		mulaisound.PlayOneShot (soundmulai);
	}
	public void showangka(){
		
	}
	public void uang10000(){
		if (name.text == "") {
			name.text = "guest";

		}
		fText.text = name.text;
		uangplayerbaru.uangplayer = 10000;
		uangcomputerbaru.uangcomputer =10000;

		hideuang.SetActive (false);
		StartCoroutine (delayangka ());
	}
	public void uang20000(){
		if (name.text == "") {
			name.text = "guest";

		}
		fText.text = name.text;
		uangplayerbaru.uangplayer = 20000;
		uangcomputerbaru.uangcomputer =20000;
		hideuang.SetActive (false);
		StartCoroutine (delayangka ());
	}
	public void uang15000(){
		if (name.text == "") {
			name.text = "guest";

		}
		fText.text = name.text;
		uangplayerbaru.uangplayer = 15000;
		uangcomputerbaru.uangcomputer =15000;
		hideuang.SetActive (false);
		StartCoroutine (delayangka ());
	}

	// Update is called once per frame
	void Update () {
		
	}
}
